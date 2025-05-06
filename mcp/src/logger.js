/**
 * Logger Configuration for Spryker Semantic Search
 *
 * This module provides a configurable Winston logger instance
 * that can be used throughout the application for consistent logging.
 *
 * @module logger
 */

import winston from 'winston';
import path from 'path';

/**
 * Creates and configures a Winston logger instance
 *
 * @param {Object} options - Logger configuration options
 * @param {string} options.serviceName - Name of the service (default: 'spryker-semantic-search')
 * @returns {winston.Logger} Configured Winston logger instance
 */
export const createLogger = (options = {}) => {
    // Extract environment variables with defaults
    const {
        LOG_LEVEL = process.env.LOG_LEVEL || 'info',
        LOG_PATH = process.env.LOG_PATH || 'logs/app.log',
        NODE_ENV = process.env.NODE_ENV || 'production'
    } = process.env;

    const serviceName = options.serviceName || 'spryker-semantic-search';
    const isProduction = NODE_ENV === 'production';

    // Define log formats
    const consoleFormat = winston.format.combine(
        winston.format.colorize(),
        winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
        winston.format.printf(({ level, message, timestamp, ...meta }) => {
            const metaStr = Object.keys(meta).length ?
                `\n${JSON.stringify(meta, null, 2)}` : '';
            return `${timestamp} [${level}] ${serviceName}: ${message}${metaStr}`;
        })
    );

    const fileFormat = winston.format.combine(
        winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
        winston.format.errors({ stack: true }),
        winston.format.splat(),
        winston.format.json()
    );

    // Configure transports
    const transports = [];

    // Add file transport for non-production environments or if explicitly enabled
    if (!isProduction || process.env.ENABLE_FILE_LOGGING === 'true') {
        transports.push(
            new winston.transports.File({
                filename: LOG_PATH,
                level: LOG_LEVEL,
                format: fileFormat,
                // Ensure directory exists
                dirname: path.dirname(LOG_PATH),
                maxsize: 10 * 1024 * 1024, // 10MB
                maxFiles: 5,
                tailable: true
            })
        );
    }

    // Create and configure the logger
    const logger = winston.createLogger({
        level: LOG_LEVEL,
        defaultMeta: { service: serviceName },
        transports,
        exitOnError: false
    });

    // Log initial configuration
    logger.debug('Logger initialized', {
        level: LOG_LEVEL,
        environment: NODE_ENV,
        serviceName
    });

    return logger;
};

/**
 * Gets a child logger with additional metadata
 *
 * @param {winston.Logger} parentLogger - The parent logger instance
 * @param {Object} metadata - Additional metadata to include with all log entries
 * @returns {winston.Logger} Child logger instance
 */
export const getChildLogger = (parentLogger, metadata = {}) => {
    return parentLogger.child(metadata);
};

export default createLogger;
